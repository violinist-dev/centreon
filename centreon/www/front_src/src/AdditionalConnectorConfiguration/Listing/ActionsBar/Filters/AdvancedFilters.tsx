import { useEffect, useMemo } from 'react';

import { useAtom } from 'jotai';
import { equals, isNil, map, pick, propEq, reject } from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  MultiAutocompleteField,
  MultiConnectedAutocompleteField,
  SelectEntry,
  TextField
} from '@centreon/ui';
import { Button } from '@centreon/ui/components';

import { getPollersEndpoint } from '../../../api/endpoints';
import {
  labelClear,
  labelName,
  labelPollers,
  labelSearch,
  labelTypes
} from '../../../translatedLabels';
import { filtersDefaultValue } from '../../../utils';
import { filtersAtom } from '../../atom';
import { NamedEntity } from '../../models';
import useLoadData from '../../useLoadData';
import { useFilterStyles } from '../useActionsStyles';

import useUpdateSearchBarBasedOnFilters from './useUpdateSearchBarBasedOnFilters';

const AdvancedFilters = (): JSX.Element => {
  const { t } = useTranslation();
  const { classes } = useFilterStyles();

  const [filters, setFilters] = useAtom(filtersAtom);
  const { reload, isLoading } = useLoadData();

  const changeName = (event): void => {
    setFilters({ ...filters, name: event.target.value });
  };

  const changeTypes = (_, types: Array<SelectEntry>): void => {
    const selectedTypes = map(
      pick(['id', 'name']),
      types || []
    ) as Array<NamedEntity>;

    setFilters({ ...filters, types: selectedTypes });
  };

  const changePollers = (_, pollers: Array<SelectEntry>): void => {
    const selectedpollers = map(
      pick(['id', 'name']),
      pollers || []
    ) as Array<NamedEntity>;

    setFilters({ ...filters, pollers: selectedpollers });
  };

  const deleteItem =
    (name) =>
    (_, option): void => {
      const newItems = reject(propEq(option.id, 'id'), filters[name]);

      setFilters({
        ...filters,
        [name]: newItems
      });
    };

  const isOptionEqualToValue = (option, selectedValue): boolean => {
    return (
      !isNil(option) &&
      equals(
        option.name.toString(),
        selectedValue.name.toString().replace('_', ' ')
      )
    );
  };

  const isClearDisabled = equals(filters, filtersDefaultValue);

  const connectorTypes = useMemo(() => {
    return filters.types.map((type) => ({
      ...type,
      name: type.name.replace('_', ' ')
    }));
  }, [filters.types]);

  const reset = (): void => {
    setFilters(filtersDefaultValue);
  };

  useEffect(() => {
    if (!isClearDisabled) {
      return;
    }

    reload();
  }, [isClearDisabled]);

  useUpdateSearchBarBasedOnFilters();

  return (
    <div className={classes.additionalFilters} data-testid="advancedFilters">
      <TextField
        fullWidth
        dataTestId={labelName}
        label={t(labelName)}
        value={filters.name}
        onChange={changeName}
      />

      <MultiAutocompleteField
        disableSortedOptions
        chipProps={{
          color: 'primary',
          onDelete: deleteItem('types')
        }}
        dataTestId={labelTypes}
        isOptionEqualToValue={isOptionEqualToValue}
        label={t(labelTypes)}
        options={[{ id: 1, name: 'VMWare 6/7' }]}
        value={connectorTypes}
        onChange={changeTypes}
      />

      <MultiConnectedAutocompleteField
        disableSortedOptions
        chipProps={{
          color: 'primary',
          onDelete: deleteItem('pollers')
        }}
        dataTestId={labelPollers}
        getEndpoint={getPollersEndpoint}
        isOptionEqualToValue={isOptionEqualToValue}
        label={t(labelPollers)}
        value={filters.pollers}
        onChange={changePollers}
      />

      <div className={classes.additionalFiltersButtons}>
        <Button
          data-testid={labelClear}
          disabled={isClearDisabled}
          size="small"
          variant="ghost"
          onClick={reset}
        >
          {t(labelClear)}
        </Button>
        <Button
          data-testid={labelSearch}
          disabled={isLoading}
          size="small"
          onClick={reload}
        >
          {t(labelSearch)}
        </Button>
      </div>
    </div>
  );
};

export default AdvancedFilters;
